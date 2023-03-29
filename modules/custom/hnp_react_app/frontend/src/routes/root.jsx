import { useEffect } from "react";
import { Form, Link, Outlet, redirect, useLoaderData, useNavigation, useSubmit } from "react-router-dom";
import { createContact, getContacts } from "../contacts";

export default function Root() {
  const { contacts, q } = useLoaderData();
  const navigation = useNavigation();
  const submit = useSubmit();
  const searching = navigation.location && new URLSearchParams(navigation.location.search).has("q");
  useEffect(() => {
    document.getElementById("q").value = q;
  }, [q]);
  return (
    <>
      <div id="sidebar">
        <h1>React Router Contacts</h1>
        <div>
          <Form id="search-form" role="search">
            <input
              id="q"
              className={searching ? "loading" : ""}
              aria-label="Search contacts"
              placeholder="Search"
              type="search"
              // The name of this input is q, that's why the URL has ?q=. 
              // If we named it search the URL would be ?search=.
              name="q"
              defaultValue={q}
              // The currentTarget is the DOM node the event is attached to, and the currentTarget.form is the input's parent form node. The submit function will serialize and submit any form you pass to it.
              // We only want to replace search results, not the page before we started searching, so we do a quick check if this is the first search or not and then decide to replace.
              // Each key stroke no longer creates new entries, so the user can click back out of the search results without having to click it 7 times
              onChange={(evt) => {
                const isFirstSearch = q === null;
                submit(evt.currentTarget.form, {
                  replace: !isFirstSearch
                });
              }}
            />
            <div
              id="search-spinner"
              aria-hidden
              hidden={!searching}
            />
            <div
              className="sr-only"
              aria-live="polite"
            ></div>
          </Form>
          <form method="post">
            <button type="submit">New</button>
          </form>
        </div>
        <nav>
          {contacts.length ? (
            <ul>
              {contacts.map(contact => <li key={contact.id}>
                <Link to={`contacts/${contact.id}`}>
                  {contact.first || contact.last ? (
                    <>
                      {contact.first} {contact.last}
                    </>
                  ) : (
                    <i>No Name</i>
                  )}{" "}
                  {contact.favorite && <span>★</span>}
                </Link>
              </li>)}
            </ul>
          ) : (
            <p>No contacts</p>
          )}
          {/* <ul>
            <li>
              <Link to={`/contacts/1`}>Your Name</Link>
            </li>
            <li>
              <Link to={`/contacts/2`}>Your Friend</Link>
            </li>
          </ul> */}
        </nav>
        <Form method="post">
          <button type="submit">New</button>
        </Form>
      </div>
      <div id="detail" className={navigation.state === 'loading' ? 'loading' : ''}>
        {/* We need to tell the root route where we want it to render its child routes. */}
        <Outlet />
      </div>
    </>
  );
}

export async function loader({ request }) {
  const url = new URL(request.url);
  const q = url.searchParams.get("q");
  // const contacts = await getContacts();
  const contacts = await getContacts(q);
  // also return `q` to make sure URL and form state are in sync.
  return { contacts, q };
}

export async function action() {
  const contact = await createContact();
  // return { contact };
  return redirect(`/contacts/${contact.id}/edit`);
}
